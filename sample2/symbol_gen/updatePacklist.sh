curpath="/disk1/symbol_gen/"

curpath=$(dirname $(readlink -f $0))

basepath=$(dirname $curpath)


packlist="/disk1/dump/packlist"
packlist=$basepath"/dump/packlist"

sodirs="shootzombies"

packs=''
for d in `echo $sodirs`
do
	cd $curpath/$d
	for f in `cat update`
	do
		version=`echo $f | awk -F, '{print $1}'`
		package=`echo $f | awk -F, '{print $2}'`
		packs="$packs $package:$version"
	done
done

for pack in `cat $packlist`
do
	count=`echo $packs | grep $pack | wc -l`
	if [ $count -eq 0 ]
	then
		line=`grep -n $pack $packlist | awk -F: '{print $1}'`
		sed -i ${line}d $packlist
	fi
done
